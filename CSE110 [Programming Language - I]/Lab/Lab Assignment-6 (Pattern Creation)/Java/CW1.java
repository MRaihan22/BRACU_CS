import java.util.Scanner;

public class CW1{
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Length: ");
        int length = sc.nextInt();
        System.out.print("Width: ");
        int width = sc.nextInt();

        for(int i = 1; i <= width; i++)
        {
            for(int j = 1; j <= length; j++)
            {
                System.out.print(j + " ");
            }
            System.out.println();
        }
    }
}