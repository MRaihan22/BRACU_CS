import java.util.Scanner;

public class CE1 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input: ");
        int n = sc.nextInt();
        System.out.println("Divisors of " + n + ": ");

        for (int i = 1; i <= n; i++)
        {
            if (n % i == 0)
            {
                System.out.println(i);
            }
        }
    }
}
