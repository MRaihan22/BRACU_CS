import java.util.Scanner;

public class CW3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Height: ");
        int height = sc.nextInt();

        for(int i = 1; i <= height; i++)
        {
            for(int j = 1; j <= height-i; j++)
            {
                System.out.print(" ");
            }
            for(int k = height-i + 1; k <= height; k++)
            {
                System.out.print(k);
            }
            System.out.println();
        }
    }
}
